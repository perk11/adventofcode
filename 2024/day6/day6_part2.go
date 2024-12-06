package main

import (
	"fmt"
	"os"
	"strings"
)

type DirectionType int32

const (
	down  DirectionType = 1
	up    DirectionType = 2
	left  DirectionType = 3
	right DirectionType = 4
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		var labyrinthMap [][]string
		labyrinthMap = make([][]string, len(lines))

		for index, line := range lines {
			labyrinthMap[index] = strings.Split(line, "")
		}
		var guardX int
		var guardY int
		for y := 0; y < len(labyrinthMap); y++ {
			for x := 0; x < len(labyrinthMap[y]); x++ {
				if labyrinthMap[y][x] == "^" {
					guardX = x
					guardY = y
					break
				}
			}
		}
		var direction = up
		var total int

		var modifiedMap [][]string
		for y := 0; y < len(labyrinthMap); y++ {
			for x := 0; x < len(labyrinthMap[y]); x++ {
				if labyrinthMap[y][x] == "^" || labyrinthMap[y][x] == "#" {
					continue
				}
				modifiedMap = make([][]string, len(labyrinthMap[y]))
				for modifiedY := 0; modifiedY < len(labyrinthMap[y]); modifiedY++ {
					modifiedMap[modifiedY] = make([]string, len(labyrinthMap[y]))
					copy(modifiedMap[modifiedY], labyrinthMap[modifiedY])
				}
				modifiedMap[y][x] = "#"
				if doesMapResultInALoop(modifiedMap, guardX, guardY, direction) {
					total++
				}
			}
		}

		println(total)
	}
}
func doesMapResultInALoop(modifiedMap [][]string, guardX int, guardY int, direction DirectionType) bool {
	visitedMapUp := make([][]bool, len(modifiedMap))
	visitedMapDown := make([][]bool, len(modifiedMap))
	visitedMapLeft := make([][]bool, len(modifiedMap))
	visitedMapRight := make([][]bool, len(modifiedMap))
	for y := 0; y < len(modifiedMap); y++ {
		visitedMapUp[y] = make([]bool, len(modifiedMap[y]))
		visitedMapDown[y] = make([]bool, len(modifiedMap[y]))
		visitedMapLeft[y] = make([]bool, len(modifiedMap[y]))
		visitedMapRight[y] = make([]bool, len(modifiedMap[y]))
	}
	visitedMapUp[guardY][guardX] = true
	var exited bool

	for {
		guardX, guardY, direction, exited = determineNextGuardPosition(modifiedMap, guardX, guardY, direction)
		if exited {
			return false
		}
		switch direction {
		case up:
			if visitedMapUp[guardY][guardX] {
				return true
			}
			visitedMapUp[guardY][guardX] = true
			break
		case down:
			if visitedMapDown[guardY][guardX] {
				return true
			}
			visitedMapDown[guardY][guardX] = true
			break
		case left:
			if visitedMapLeft[guardY][guardX] {
				return true
			}
			visitedMapLeft[guardY][guardX] = true
			break
		case right:
			if visitedMapRight[guardY][guardX] {
				return true
			}
			visitedMapRight[guardY][guardX] = true
		}
	}

}
func determineNextGuardPosition(labyrinthMap [][]string, guardX int, guardY int, direction DirectionType) (int, int, DirectionType, bool) {
	var newX = guardX
	var newY = guardY
	switch direction {
	case down:
		newY++
		break
	case up:
		newY--
		break
	case left:
		newX--
		break
	case right:
		newX++
		break
	}
	if newY < 0 || newX < 0 || newY >= len(labyrinthMap) || newX >= len(labyrinthMap[newY]) {
		return newX, newY, direction, true
	}

	if labyrinthMap[newY][newX] == "." || labyrinthMap[newY][newX] == "^" {
		return newX, newY, direction, false
	}
	var newDirection DirectionType
	switch direction {
	case down:
		newDirection = left
		break
	case up:
		newDirection = right
		break
	case left:
		newDirection = up
		break
	case right:
		newDirection = down
		break
	}

	return guardX, guardY, newDirection, false
}
