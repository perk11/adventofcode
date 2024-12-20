package main

import (
	"fmt"
	"maps"
	"os"
	"slices"
	"strings"
)

type Tile string

const (
	wall  Tile = "#"
	track Tile = "."
	start Tile = "S"
	end   Tile = "E"
)

type Coordinates struct {
	x, y int
}
type Cheat struct {
	entry, start, end Coordinates
	savings           int
}
type TrackPart struct {
	start, end, visitedCoordinate Coordinates
}

// var knownNextCoordinates map[Coordinates]Coordinates
var distances map[TrackPart]int

//1334 too low
//1362 too low
//1368 too high

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		raceMap := make([][]Tile, len(lines))
		var startCoordinates Coordinates
		for y, line := range lines {
			raceMap[y] = make([]Tile, len(line))
			for x := 0; x < len(line); x++ {
				raceMap[y][x] = Tile(line[x])
				if raceMap[y][x] == start {
					startCoordinates = Coordinates{x, y}
				}
			}
		}
		currentCoordinates := startCoordinates
		var previousCoordinates Coordinates
		if fileName == "testInput" {
			previousCoordinates = Coordinates{currentCoordinates.x, currentCoordinates.y + 1}
		} else if fileName == "input" {
			previousCoordinates = Coordinates{currentCoordinates.x, currentCoordinates.y - 1}
		}
		total := 0
		visitedTiles := make(map[Coordinates]bool)
		distances = make(map[TrackPart]int)
		allCheats := make([]Cheat, 0)
		for {
			cheats := findPossibleCheats(currentCoordinates, raceMap)
			for _, cheat := range cheats {
				_, ok := visitedTiles[cheat.end]
				if ok {
					continue
				}
				distanceWithoutCheat := distanceBetweenCoordinates(TrackPart{cheat.entry, cheat.end, previousCoordinates}, raceMap)
				cheat.savings = distanceWithoutCheat - 2
				allCheats = append(allCheats, cheat)
				if cheat.savings >= 100 {
					total++
				}
			}
			visitedTiles[currentCoordinates] = true
			nextCoordinates := nextTrackCoordinate(currentCoordinates, previousCoordinates, raceMap)
			if nextCoordinates == nil {
				break
			}
			previousCoordinates = currentCoordinates
			currentCoordinates = *nextCoordinates
		}
		cheatsByPico := make(map[int]int)
		for _, cheat := range allCheats {
			_, ok := cheatsByPico[cheat.savings]
			if ok {
				cheatsByPico[cheat.savings]++
			} else {
				cheatsByPico[cheat.savings] = 1
			}
		}
		cheatsByPicoSorted := slices.Sorted(maps.Keys(cheatsByPico))
		for _, key := range cheatsByPicoSorted {
			fmt.Printf("%d - %d\n", key, cheatsByPico[key])
		}
		println(total)
	}
}
func distanceBetweenCoordinates(trackPart TrackPart, raceMap [][]Tile) int {
	distance := 0
	knownDistance, ok := distances[trackPart]
	if ok {
		return knownDistance
	}
	currentCoordinate := trackPart.start
	previousCoordinate := trackPart.visitedCoordinate
	for {
		distance++
		nextCoordinate := nextTrackCoordinate(currentCoordinate, previousCoordinate, raceMap)
		if nextCoordinate == nil {
			panic("Failed to calc distance")
		}
		if *nextCoordinate == trackPart.end {
			distances[trackPart] = distance
			return distance
		}
		previousCoordinate = currentCoordinate
		currentCoordinate = *nextCoordinate
	}
}
func nextTrackCoordinate(start Coordinates, visited Coordinates, raceMap [][]Tile) *Coordinates {
	if start.x < len(raceMap[0])-1 && !(start.x+1 == visited.x && start.y == visited.y) && raceMap[start.y][start.x+1] != wall {
		return &Coordinates{start.x + 1, start.y}
	}
	if start.x > 1 && !(start.x-1 == visited.x && start.y == visited.y) && raceMap[start.y][start.x-1] != wall {
		return &Coordinates{start.x - 1, start.y}
	}

	if start.y < len(raceMap)-1 && !(start.x == visited.x && start.y+1 == visited.y) && raceMap[start.y+1][start.x] != wall {
		return &Coordinates{start.x, start.y + 1}
	}

	if start.y > 1 && !(start.x == visited.x && start.y-1 == visited.y) && raceMap[start.y-1][start.x] != wall {
		return &Coordinates{start.x, start.y - 1}
	}

	return nil
}
func findPossibleCheats(cheatEntrance Coordinates, raceMap [][]Tile) []Cheat {
	possibleCheats := make([]Cheat, 0)
	if cheatEntrance.x > 1 {
		left := getCheatIfValid(Coordinates{cheatEntrance.x - 1, cheatEntrance.y}, cheatEntrance, raceMap)
		if left != nil {
			possibleCheats = append(possibleCheats, *left)
		}
	}

	if cheatEntrance.x < len(raceMap[0])-1 {
		right := getCheatIfValid(Coordinates{cheatEntrance.x + 1, cheatEntrance.y}, cheatEntrance, raceMap)
		if right != nil {
			possibleCheats = append(possibleCheats, *right)
		}
	}

	if cheatEntrance.y < len(raceMap)-1 {
		down := getCheatIfValid(Coordinates{cheatEntrance.x, cheatEntrance.y + 1}, cheatEntrance, raceMap)
		if down != nil {
			possibleCheats = append(possibleCheats, *down)
		}
	}
	if cheatEntrance.y > 1 {
		up := getCheatIfValid(Coordinates{cheatEntrance.x, cheatEntrance.y - 1}, cheatEntrance, raceMap)
		if up != nil {
			possibleCheats = append(possibleCheats, *up)
		}
	}

	return possibleCheats
}
func getCheatIfValid(cheatStart Coordinates, cheatEntrance Coordinates, raceMap [][]Tile) *Cheat {
	if raceMap[cheatStart.y][cheatStart.x] != wall {
		return nil
	}
	cheatEnd := nextTrackCoordinate(cheatStart, cheatEntrance, raceMap)
	if cheatEnd == nil {
		return nil
	}
	cheat := Cheat{
		entry: cheatEntrance,
		start: cheatStart,
		end:   *cheatEnd,
	}
	return &cheat
}
