package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

type coordinates struct {
	x, y int
}

var maxX, maxY int

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		maxX = len(lines[0]) - 1
		maxY = len(lines) - 1
		heightMap := make([][]int, len(lines))
		for lineIndex, line := range lines {
			heights := strings.Split(line, "")
			heightMap[lineIndex] = make([]int, len(heights))
			for heightIndex, height := range heights {
				heightMap[lineIndex][heightIndex], _ = strconv.Atoi(height)
			}
		}
		total := 0
		for y := 0; y <= maxY; y++ {
			for x := 0; x <= maxX; x++ {
				if heightMap[y][x] != 0 {
					continue
				}
				visited := make(map[coordinates]bool)
				score := calculateTrailHadScore(heightMap, coordinates{x, y}, 0, &visited, false)
				//fmt.Printf("%d, %d: %d\n", x, y, score)
				total += score
			}
		}
		println(total)
		total = 0
		for y := 0; y <= maxY; y++ {
			for x := 0; x <= maxX; x++ {
				if heightMap[y][x] != 0 {
					continue
				}
				visited := make(map[coordinates]bool)
				score := calculateTrailHadScore(heightMap, coordinates{x, y}, 0, &visited, true)
				total += score
			}
		}
		println(total)
	}
}
func calculateTrailHadScore(heightMap [][]int, trailHead coordinates, level int, visitedPeaks *map[coordinates]bool, part2 bool) int {
	if level == 9 {
		_, exists := (*visitedPeaks)[trailHead]
		if exists {
			if part2 {
				return 1
			} else {
				return 0
			}
		} else {
			(*visitedPeaks)[trailHead] = true
			return 1
		}
	}
	score := 0
	neighbours := getNeighborCoordinates(trailHead)
	for _, neighbor := range neighbours {
		if heightMap[neighbor.y][neighbor.x] == level+1 {
			score += calculateTrailHadScore(heightMap, neighbor, level+1, visitedPeaks, part2)
		}
	}
	return score
}

func getNeighborCoordinates(startCoordinate coordinates) []coordinates {
	result := make([]coordinates, 0)
	if startCoordinate.x > 0 {
		result = append(result, coordinates{startCoordinate.x - 1, startCoordinate.y})
	}
	if startCoordinate.y > 0 {
		result = append(result, coordinates{startCoordinate.x, startCoordinate.y - 1})
	}
	if startCoordinate.x < maxX {
		result = append(result, coordinates{startCoordinate.x + 1, startCoordinate.y})
	}
	if startCoordinate.y < maxY {
		result = append(result, coordinates{startCoordinate.x, startCoordinate.y + 1})
	}

	return result
}
